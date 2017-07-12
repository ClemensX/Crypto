package de.fehrprice.crypto.test;

import static org.junit.Assert.*;

import java.math.BigInteger;
import java.text.DecimalFormat;

import org.junit.Test;

import de.fehrprice.crypto.RSA;

public class Speed {

	int conf[][] = {
			{ 32, 100},
			{ 64, 100},
			{ 128, 100},
			{ 256, 100},
			{ 512, 50},
			{ 1024, 10},
			{ 2048, 1},
			{ 4096, 1},
			{ 4096*2, 1}
	};

	@Test
	public final void info() {
		//
		BigInteger prim = BigInteger.valueOf(2);
		prim = prim.pow(1024);
		String primString = prim.toString();
		System.out.println(" 2 ^ 1024 == [" + primString.length() + " digits] " + primString);
		prim = BigInteger.valueOf(2);
		prim = prim.pow(80);
		primString = prim.toString();
		System.out.println(" 2 ^ 80 == [" + primString.length() + " digits] " + primString);
		prim = BigInteger.valueOf(2);
		prim = prim.pow(112);
		primString = prim.toString();
		System.out.println(" 2 ^ 112 == [" + primString.length() + " digits] " + primString);
	}
	
	@Test
	public final void test() {
		// warm up JIT:
		for (int i = 0; i < 50; i++) {
			RSA rsa = new RSA();
			rsa.generateKeys(64);
		}
		System.out.println("RSA Generation times: ");
		for (int i = 0; i < conf.length; i++)
		  measure(conf[i][0], conf[i][1]);
	}
	
	private void measure(int bitlen, int count) {
		long start = System.nanoTime();
		for (int i = 0; i < count; i++) {
			RSA rsa = new RSA();
			rsa.generateKeys(bitlen);
		}
		long now = System.nanoTime();
		long duration = now - start;
		duration /= count;
		double duration_seconds = ((double) duration) / 1E9;
		DecimalFormat df = new DecimalFormat();
		df.setMaximumFractionDigits(4);
		System.out.println("RSA " + bitlen + ": [s] " + df.format(duration_seconds));
	}
	
}
