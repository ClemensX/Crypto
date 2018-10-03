package de.fehrprice.crypto;

public class Conv {
	/**
	 * Extend byte array to a fixed length by appending zero bytes
	 * @param finalLength
	 * @param input
	 * @return
	 */
	public static byte[] extendWithZeroBytes(int finalLength, byte[] input) {
		if (input.length >= finalLength) {
			return input;
		}
		byte[] res = new byte[finalLength];
		System.arraycopy(input, 0, res, 0, input.length);
		for (int i = input.length; i < finalLength; i++) {
			res[i] = 0;
		}
		return res;
	}

	/**
	 * Extend byte array at beginning (low index) to a fixed length by prepending zero bytes
	 * @param finalLength
	 * @param input
	 * @return
	 */
	public static byte[] prependWithZeroBytes(int finalLength, byte[] input) {
		if (input.length >= finalLength) {
			return input;
		}
		byte[] res = new byte[finalLength];
		System.arraycopy(input, 0, res, finalLength - input.length, input.length);
		for (int i = 0; i < finalLength - input.length; i++) {
			res[i] = 0;
		}
		return res;
	}
}
